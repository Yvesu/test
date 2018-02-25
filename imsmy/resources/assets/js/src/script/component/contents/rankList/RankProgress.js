import React, { Component } from 'react'

import { Card, Progress  } from 'antd'

//排行榜 - 带进度条

class RankProgress extends Component {
  constructor(props){
    super(props)
    this.state={
      title:"",
      rankData:[]
    }
  }

  render(){
    return(
    <Card title={this.state.title} className="rank_progress">
      <p className="rank_progress_title">
        <span>排名</span>
        <span>关键词</span>
        <span>搜索指数</span>
        <span>热度指数</span>
      </p>
      <ul>
        {
          this.state.rankData.map((value,index)=>{
            return(
              <li key={index}>
                <span>{value.ranking}</span>
                <span><b>{value.key_word}</b></span>
                <span>{value.search_number}</span>
                <span><Progress percent={value.host_number} strokeWidth={5} status="active" /></span>
              </li>
            )
          })
        }
      </ul>
    </Card>
    )
  }
  componentDidMount(){
    fetch("./data/rankhost.json")
    .then((response)=>response.json())
    .then((res) => {
      this.setState({
        title:res.title,
        rankData:res.data
      })
    })
  }

}

export default RankProgress
