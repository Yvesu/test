import React, { Component } from 'react'

import { Card, Badge } from 'antd'
//排行榜 - 不带进度条的

class RankList extends Component {
  constructor(props){
    super(props)
    this.state={
      title:"",
      rankData:[]
    }
  }
  render(){
    return(
    <Card title={this.state.title} className="rank_list">
       <p className="rank_list_title">
          <span>排名</span>
          <span>关键词</span>
          <span>搜索指数</span>
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
          rankData:res.data
        })
      })
  }

}

export default RankList
