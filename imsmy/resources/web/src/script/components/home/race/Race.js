import React, { Component } from 'react'
import { Layout,Button, Tabs} from 'antd'
const TabPane = Tabs.TabPane
import TabsPaneTitle from './common/TabsPaneTitle'
import RaceHot from './tabPane/RaceHot'
import RacePartake from './tabPane/RacePartake'
import RaceNewStart from './tabPane/RaceNewStart'
import RaceSoonEnd from './tabPane/RaceSoonEnd'
import RaceWhole from './tabPane/RaceWhole'
import RaceLaunch from './tabPane/RaceLaunch'
class Race extends Component {
  constructor(props){
    super(props)
    this.state={
      activeNowKey:'1',
      isActiveOne:true,
      isActiveTwo:false,
      isActiveThr:false,
      isActiveFour:false,
      isActiveFive:false,
      isActiveSix:false,
    }
  }
  handleChangeActiveKey = (key)=>{
    this.setState({
      activeNowKey:key
    })
    if(key==='1'){
      this.setState({
        isActiveOne:true,
        isActiveTwo:false,
        isActiveThr:false,
        isActiveFour:false,
        isActiveFive:false,
        isActiveSix:false,
      })
    }
    if(key==='2'){
      this.setState({
        isActiveOne:false,
        isActiveTwo:true,
        isActiveThr:false,
        isActiveFour:false,
        isActiveFive:false,
        isActiveSix:false,
      })
    }
    if(key==='3'){
      this.setState({
        isActiveOne:false,
        isActiveTwo:false,
        isActiveThr:true,
        isActiveFour:false,
        isActiveFive:false,
        isActiveSix:false,
      })
    }
    if(key==='4'){
      this.setState({
        isActiveOne:false,
        isActiveTwo:false,
        isActiveThr:false,
        isActiveFour:true,
        isActiveFive:false,
        isActiveSix:false,
      })
    }
    if(key==='5'){
      this.setState({
        isActiveOne:false,
        isActiveTwo:false,
        isActiveThr:false,
        isActiveFour:false,
        isActiveFive:true,
        isActiveSix:false,
      })
    }
    if(key==='6'){
      this.setState({
        isActiveOne:false,
        isActiveTwo:false,
        isActiveThr:false,
        isActiveFour:false,
        isActiveFive:false,
        isActiveSix:true,
      })
    }
  }
  render(){
    return(
      <div className='home_content_races'>
        <Tabs activeKey={this.state.activeNowKey}
           onChange={this.handleChangeActiveKey}
           className='race_table_tabs' >

          <TabPane tab={<TabsPaneTitle titleText={'热门'}
                // imgUrl="./img/hot.png"
                // imgUrlHover="./img/hot_h.png"
                imgUrl="http://img.cdn.hivideo.com/web/img/hot.png"
                imgUrlHover="http://img.cdn.hivideo.com/web/img/hot_h.png"
                isActive={this.state.isActiveOne}
            />} key="1">

            <RaceHot />

          </TabPane>
          <TabPane tab={<TabsPaneTitle titleText={'已参与'}
            // imgUrl="./img/already_selected.png"
            // imgUrlHover="./img/already_selected_h.png"
            imgUrl="http://img.cdn.hivideo.com/web/img/already_selected.png"
            imgUrlHover="http://img.cdn.hivideo.com/web/img/already_selected_h.png"
            isActive={this.state.isActiveTwo}
          />} key="2">

            <RacePartake />

          </TabPane>
          <TabPane tab={<TabsPaneTitle titleText={'最新发起'}
            // imgUrl="./img/new.png"
            // imgUrlHover="./img/new_h.png"
            imgUrl="http://img.cdn.hivideo.com/web/img/new.png"
            imgUrlHover="http://img.cdn.hivideo.com/web/img/new_h.png"
            isActive={this.state.isActiveThr}
          />} key="3">

            <RaceNewStart />

          </TabPane>
          <TabPane tab={<TabsPaneTitle titleText={'即将截止'}
            // imgUrl="./img/close_to_the_end.png"
            // imgUrlHover="./img/close_to_the_end_h.png"
            imgUrl="http://img.cdn.hivideo.com/web/img/close_to_the_end.png"
            imgUrlHover="http://img.cdn.hivideo.com/web/img/close_to_the_end_h.png"
            isActive={this.state.isActiveFour}
          />} key="4">

            <RaceSoonEnd />

          </TabPane>
          <TabPane tab={<TabsPaneTitle titleText={'全部'}
            // imgUrl="./img/all.png"
            // imgUrlHover="./img/all_h.png"
            imgUrl="http://img.cdn.hivideo.com/web/img/all.png"
            imgUrlHover="http://img.cdn.hivideo.com/web/img/all_h.png"

            isActive={this.state.isActiveFive}
          />} key="5">

            <RaceWhole />

          </TabPane>
          <TabPane tab={<TabsPaneTitle titleText={'发起竞赛'}
            imgUrl="http://img.cdn.hivideo.com/web/img/release.png"
            imgUrlHover="http://img.cdn.hivideo.com/web/img/release_h.png"
            // imgUrl="./img/release.png"
            // imgUrlHover="./img/release_h.png"
            isActive={this.state.isActiveSix}
          />} key="6">

            <RaceLaunch />

          </TabPane>
        </Tabs>

      </div>
    )
  }
  componentDidMount(){
    // console.log(this.props);

  }
  // componentWillReceiveProps(nextProps){
  //   console.log(document.documentElement.scrollTop);
  // }
}

export default Race
