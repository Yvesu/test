import React, { Component } from 'react'
import { Card, Badge } from 'antd'


class RankList extends Component {
  constructor(props){
    super(props)
    this.state={
      title:"",
      title_em:'',
      rankData:[],
      list_title:[]
    }
  }
  render(){
    return(
      <Card title={
        <span style={{fontSize:18,color:"#4a4a4a"}}>
              {this.state.title}
          <em>{this.state.title_em}</em>
        </span> } className="rank_list">
         <p className="rank_list_title">
           {
             this.state.list_title.map((value,index)=>{
               return(
                 <span key={index}>{value.title}</span>
               )
             })
           }
          </p>
        <ul>
          {
            this.state.rankData.map((value,index)=>{
              return(
                <li key={index}>
                    <span style={{background:`${(index=="0")? "#ff0000" : `${(index=="1")? "#ff6c00" : `${(index=="2")? "#ffb842" : ""}`}`}`}}>
                      {value.ranking}
                    </span>
                    <span style={{color:`${(index=="0"||index=="1"||index=="2")? "#108ee9" : "#4a4a4a" }`}}>
                      {value.key_word}
                      <Badge count={`${(value.new)? "New" : "0"}`} style={{minWidth:"35px",marginLeft:"15px"}}/>
                    </span>
                    <span>{value.search_number}</span>
                </li>
              )
            })
          }
        </ul>
      </Card>
    )
  }
  componentDidMount(){
    fetch(this.props.uri)
      .then((response)=>response.json())
      .then((res) => {
        this.setState({
          title:res.title,
          title_em:res.title_em,
          rankData:res.data,
          list_title:res.list_title
        })
      })
  }
}

export default RankList
